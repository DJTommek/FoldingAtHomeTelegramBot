<?php

namespace TelegramWrapper\Command;


use Folding;
use FoldingAtHome\Exceptions\ApiErrorException;
use FoldingAtHome\Exceptions\ApiTimeoutException;
use FoldingAtHome\Exceptions\GeneralException;
use FoldingAtHome\Exceptions\NotFoundException;
use FoldingAtHome\RequestTeam;
use FoldingAtHome\RequestDonor;
use Icons;
use React\EventLoop\StreamSelectLoop;
use TelegramWrapper\Telegram;
use Tracy\Debugger;
use Tracy\ILogger;
use unreal4u\TelegramAPI\Telegram\Methods\SendChatAction;
use unreal4u\TelegramAPI\Telegram\Types\Inline\Keyboard\Markup;
use unreal4u\TelegramAPI\Telegram\Types\Update;
use unreal4u\TelegramAPI\TgLog;

abstract class Command
{
	protected $update;
	protected $tgLog;
	protected $loop;
	protected $user;

	protected $command = null;
	protected $params = [];

	const CMD_DONOR = '/donor';
	const CMD_TEAM = '/team';

	public function __construct(Update $update, TgLog $tgLog, StreamSelectLoop $loop, \User $user) {
		$this->update = $update;
		$this->tgLog = $tgLog;
		$this->loop = $loop;
		$this->user = $user;

		$this->command = Telegram::getCommand($update);
		$this->params = Telegram::getParams($update);
	}

	public function getChatId() {
		return $this->update->message->chat->id;
	}

	public function getFromId() {
		return $this->update->message->from->id;
	}

	public function isPm() {
		return Telegram::isPM($this->update);
	}

	public function reply(string $text, $replyMarkup = null) {
		$msg = new \TelegramWrapper\SendMessage($this->getChatId(), $text, null, $replyMarkup);
		$this->run($msg->msg);
	}

	public function sendAction(string $action = 'typing') {
		$chatAction = new SendChatAction();
		$chatAction->chat_id = $this->getChatId();
		$chatAction->action = $action;
		$this->run($chatAction);
	}

	public function run($objectToSend) {
		$promise = $this->tgLog->performApiRequest($objectToSend);
		$this->loop->run();

		$promise->then(
			function ($response) {
				Debugger::log($response);
				Debugger::log('TG API Request successfull. Response: ' . $response);
			},
			function (\Exception $exception) {
				Debugger::log(sprintf('Error while API request: "%s"', $exception->getMessage()), ILogger::EXCEPTION);
				throw $exception;
			}
		);
	}

	/**
	 * @param $foldingDonorId
	 * @throws GeneralException
	 */
	protected function processStatsDonor($foldingDonorId) {
		$this->sendAction();
		$replyMarkup = new Markup();

		try {
			$donorStats = (new RequestDonor($foldingDonorId))->load();
		} catch (NotFoundException $exception) {
			$this->reply(sprintf('%s Donor <b>%s</b> not found', Icons::ERROR, htmlentities($foldingDonorId)), $replyMarkup);
			return;
		} catch (ApiErrorException $exception) {
			$this->reply(sprintf('%s <b>Error</b>: Folding@home API responded with error <b>%s</b>', Icons::ERROR, htmlentities($exception->getMessage())), $replyMarkup);
			return;
		} catch (ApiTimeoutException $exception) {
			$replyMarkup->inline_keyboard[] = [
				$this->addDonorRefreshButton($foldingDonorId)
			];
			$this->reply(sprintf('%s <b>Error</b>: Folding@home API is not responding, try again later.', Icons::ERROR), $replyMarkup);
			return;
		} catch (GeneralException $exception) {
			$this->reply(sprintf('%s <b>Error</b>: Unhandled Folding@home error occured, error was saved and admin was notified.', Icons::ERROR), $replyMarkup);
			throw $exception;
		}
		[$text, $buttons] = Folding::formatDonorStats($donorStats);

		$replyMarkup->inline_keyboard[] = [
			$this->addDonorRefreshButton($foldingDonorId),
			[
				'text' => sprintf('%s Set donor as default', Icons::DEFAULT),
				'callback_data' => sprintf('/setnick %d %s', $donorStats->id, base64_encode($donorStats->name)),
			]
		];
		$replyMarkup->inline_keyboard = array_merge($replyMarkup->inline_keyboard, $buttons);
		$this->reply($text, $replyMarkup);
	}

	private function addDonorRefreshButton($foldingUserId) {
		return [
			'text' => sprintf('%s Refresh', Icons::REFRESH),
			'callback_data' => sprintf('%s %s', Command::CMD_DONOR, $foldingUserId),
		];
	}

	private function addTeamRefreshButton($foldingUserId) {
		return [
			[
				'text' => sprintf('%s Refresh', Icons::REFRESH),
				'callback_data' => sprintf('%s %s', Command::CMD_DONOR, $foldingUserId),
			],
		];
	}

	/**
	 * @param $foldingTeamId
	 * @throws GeneralException
	 */
	protected function processStatsTeam($foldingTeamId) {
		$replyMarkup = new Markup();
		$this->sendAction();
		try {
			$teamStats = (new RequestTeam($foldingTeamId))->load();
		} catch (NotFoundException $exception) {
			$this->reply(sprintf('%s Team <b>%s</b> not found', Icons::ERROR, htmlentities($foldingTeamId)), $replyMarkup);
			return;
		} catch (ApiErrorException $exception) {
			$this->reply(sprintf('%s <b>Error</b>: Folding@home API responded with error <b>%s</b>', Icons::ERROR, htmlentities($exception->getMessage())), $replyMarkup);
			return;
		} catch (ApiTimeoutException $exception) {
			$replyMarkup->inline_keyboard[] = $this->addTeamRefreshButton($foldingTeamId);
			$this->reply(sprintf('%s <b>Error</b>: Folding@home API is not responding, try again later.', Icons::ERROR), $replyMarkup);
			return;
		} catch (GeneralException $exception) {
			$this->reply(sprintf('%s <b>Error</b>: Unhandled Folding@home error occured, error was saved and admin was notified.', Icons::ERROR), $replyMarkup);
			throw $exception;
		}
		[$text, $buttons] = Folding::formatTeamStats($teamStats);

		$replyMarkup = new Markup();
		$replyMarkup->inline_keyboard[] = $this->addTeamRefreshButton($teamStats->id);
		$replyMarkup->inline_keyboard[0][] = [
			'text' => Icons::DEFAULT . ' Set team as default',
			'callback_data' => '/setteam ' . $teamStats->id . ' ' . base64_encode($teamStats->name),
		];
		$replyMarkup->inline_keyboard = array_merge($replyMarkup->inline_keyboard, $buttons);
		$this->reply($text, $replyMarkup);
	}
}